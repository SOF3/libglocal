// This is a generated file. Not intended for manual editing.
package io.github.sof3.libglocal.intellij.psi;

import java.util.List;
import org.jetbrains.annotations.*;
import com.intellij.psi.PsiElement;

public interface LgcMessageGroup extends PsiElement {

  @NotNull
  LgcEnd getEnd();

  @Nullable
  LgcMessageBlock getMessageBlock();

  @Nullable
  LgcMessageGroup getMessageGroup();

  @Nullable
  PsiElement getEquals();

  @Nullable
  PsiElement getIndentDecrease();

  @Nullable
  PsiElement getIndentIncrease();

  @NotNull
  PsiElement getGroupId();

}
