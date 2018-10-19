// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi.impl;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.lang.ASTNode;
import com.intellij.psi.PsiElement;
import com.intellij.psi.PsiElementVisitor;
import com.intellij.psi.util.PsiTreeUtil;
import static io.github.sof3.libglocal.intellij.parser.LgcElements.*;
import com.intellij.extapi.psi.ASTWrapperPsiElement;
import io.github.sof3.libglocal.intellij.psi.*;

public class LgcAttributeValueImpl extends ASTWrapperPsiElement implements LgcAttributeValue {

  public LgcAttributeValueImpl(@NotNull ASTNode node) {
    super(node);
  }

  public void accept(@NotNull LgcVisitor visitor) {
    visitor.visitAttributeValue(this);
  }

  public void accept(@NotNull PsiElementVisitor visitor) {
    if (visitor instanceof LgcVisitor) accept((LgcVisitor)visitor);
    else super.accept(visitor);
  }

  @Override
  @Nullable
  public LgcArgumentAttributeValue getArgumentAttributeValue() {
    return findChildByClass(LgcArgumentAttributeValue.class);
  }

  @Override
  @Nullable
  public LgcLiteralAttributeValue getLiteralAttributeValue() {
    return findChildByClass(LgcLiteralAttributeValue.class);
  }

  @Override
  @Nullable
  public LgcMessageAttributeValue getMessageAttributeValue() {
    return findChildByClass(LgcMessageAttributeValue.class);
  }

  @Override
  @Nullable
  public LgcNumberAttributeValue getNumberAttributeValue() {
    return findChildByClass(LgcNumberAttributeValue.class);
  }

}
